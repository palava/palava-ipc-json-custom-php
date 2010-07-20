/**
 * Copyright 2010 CosmoCode GmbH
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

package de.cosmocode.palava.ipc.json.custom.php.explorer;

import com.google.common.base.Functions;
import com.google.common.collect.Iterables;
import com.google.common.collect.Sets;
import com.google.inject.Singleton;
import de.cosmocode.collections.utility.UtilityList;
import de.cosmocode.commons.reflect.Classpath;
import de.cosmocode.commons.reflect.Packages;
import de.cosmocode.commons.reflect.Reflection;
import de.cosmocode.palava.ipc.IpcCall;
import de.cosmocode.palava.ipc.IpcCommand;
import de.cosmocode.palava.ipc.IpcCommandExecutionException;
import de.cosmocode.palava.ipc.cache.Cached;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.util.Map;
import java.util.Set;

/**
 * @author Tobias Sarnowski
 */
@Singleton
@Cached
@IpcCommand.Description("Returns a list of all commands available in the JVM.")
@IpcCommand.Param(name = "packages", description = "a list of packages to search in")
@IpcCommand.Return(name = "commands", description = "a list of all commands")
final class Commands implements IpcCommand {
    private static final Logger LOG = LoggerFactory.getLogger(Commands.class);

    @Override
    public void execute(IpcCall call, Map<String, Object> result) throws IpcCommandExecutionException {
        UtilityList<Object> packages = call.getArguments().getList("packages");

        Classpath cp = Reflection.defaultClasspath();
        Packages pkgs = cp.restrictTo(Iterables.transform(packages, Functions.toStringFunction()));

        Set<Class> commands = Sets.newHashSet();

        for (Class cls: pkgs.subclassesOf(IpcCommand.class)) {
            commands.add(cls);
        }

        result.put("commands", commands);
    }
}